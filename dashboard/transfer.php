<?php
require_once '../includes/config.php';
if (!Security::isLoggedIn()) {
    redirectWithMessage('../public/login.php', 'Please login to make transfers.', 'danger');
}
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare('SELECT account_balance, account_status, kyc_status FROM users WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user || $user['account_status'] !== 'active') {
    redirectWithMessage('index.php', 'Your account is not active.', 'danger');
}
if (($user['kyc_status'] ?? 'none') !== 'verified') {
    redirectWithMessage('security.php', 'KYC verification is required before this transaction type. Please complete KYC in Security settings.', 'info');
}
$csrf = Security::generateCSRFToken();
require_once '../includes/header.php';
?>
<section class="hero"><div class="container"><h1>Make a Transfer</h1><p>Protected by Transfer PIN + Security PIN verification.</p></div></section>
<section style="padding:2rem 0;"><div class="container">
<div class="glass-card" style="margin-bottom:1rem;"><p><strong>Available Balance:</strong> <?php echo formatCurrency($user['account_balance']); ?></p></div>
<div class="glass-card">
<form id="transferForm" method="POST" action="transfer_process.php">
<input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
<input type="hidden" name="authorization_pin" id="authorization_pin_hidden">
<input type="hidden" name="payment_pin" id="payment_pin_hidden">
<input type="hidden" name="secure_pass_pin" id="secure_pass_pin_hidden">
<input type="hidden" name="transfer_pin" id="transfer_pin_hidden">
<div class="form-group"><label>Transfer Type</label><select class="form-control" name="transfer_type" required><option value="internal">Internal</option><option value="external">External</option><option value="wire">Wire</option></select></div>
<div class="form-group"><label>Recipient Account</label><input class="form-control" type="text" name="recipient_account" required></div>

<div class="form-group">
  <label for="bank_search">Recipient Bank (U.S. bank search)</label>
  <input class="form-control" type="text" id="bank_search" placeholder="Search U.S. bank name (min 2 characters)">
  <small class="text-muted">Loaded from FDIC institution directory. Search or scroll the full list. If not found, use manual entry below.</small>
</div>
<div class="form-group">
  <label for="recipient_bank">Selected/Manual Bank Name</label>
  <input class="form-control" type="text" name="recipient_bank" id="recipient_bank" placeholder="Selected bank appears here or type manually" required>
</div>
<div id="bank_results" class="card" style="margin-bottom:1rem;"><div class="card-body"><label for="bank_scroll_list">Bank List</label><select id="bank_scroll_list" class="form-control" size="8" style="max-height:260px; overflow:auto;"></select><p id="bank_status" style="margin:.5rem 0 0 0;color:var(--text-muted)">Loading bank directory…</p></div></div>

<div class="form-group"><label>Amount</label><input class="form-control" type="number" step="0.01" min="1" name="amount" required></div>
<div class="form-group"><label>Description</label><input class="form-control" type="text" name="description" required minlength="5"></div>
<button type="button" class="btn btn-primary" id="openPinModalBtn">Continue</button>
</form>
</div></div></section>

<div id="pinModal" class="modal-overlay" style="display:none;">
  <div class="modal glass-card" style="max-width:420px;width:90%;">
    <h3 id="pinPromptTitle">Enter PIN</h3>
    <p id="pinPromptMessage">Enter your PIN to continue.</p>
    <input id="pinInput" class="form-control" type="password" autocomplete="off">
    <div id="pinError" class="alert alert-error" style="display:none;margin-top:.5rem;"></div>
    <div style="display:flex;justify-content:flex-end;gap:.5rem;margin-top:1rem;">
      <button class="btn btn-secondary" type="button" onclick="closePinModal()">Cancel</button>
      <button class="btn btn-primary" type="button" id="verifyPinBtn">Verify</button>
    </div>
  </div>
</div>

<script>
const pinStages = [
  {type:'transfer', title:'Transfer PIN', message:'Enter your 4-digit Transfer PIN.'},
  {type:'authorization', title:'Authorization PIN', message:'Enter your Authorization PIN.'},
  {type:'payment', title:'Payment PIN', message:'Enter your Payment PIN.'},
  {type:'secure_pass', title:'Secure PIN', message:'Enter your Secure PIN.'}
];
let stageIndex = 0;
const storedPins = {};

function closePinModal(){ document.getElementById('pinModal').style.display='none'; stageIndex=0; document.getElementById('pinInput').value=''; }
function showStage(){
  const s = pinStages[stageIndex];
  document.getElementById('pinPromptTitle').textContent = s.title;
  document.getElementById('pinPromptMessage').textContent = s.message;
  document.getElementById('pinInput').value = '';
  document.getElementById('pinError').style.display='none';
}
async function verifyCurrentStage(){
  const s = pinStages[stageIndex];
  const pin = document.getElementById('pinInput').value.trim();
  const fd = new FormData(); fd.append('type', s.type); fd.append('pin', pin);
  const res = await fetch('verify_pin.php', {method:'POST', body: fd});
  const data = await res.json();
  if(!data.success){
    const el = document.getElementById('pinError');
    el.textContent = data.message || 'Invalid PIN';
    el.style.display = 'block';
    if(data.requires_setup){
      window.location.href = 'setup_pins.php';
    }
    return;
  }
  storedPins[s.type] = pin;
  stageIndex++;
  if(stageIndex >= pinStages.length){
    document.getElementById('transfer_pin_hidden').value = storedPins.transfer || '';
    document.getElementById('authorization_pin_hidden').value = storedPins.authorization || '';
    document.getElementById('payment_pin_hidden').value = storedPins.payment || '';
    document.getElementById('secure_pass_pin_hidden').value = storedPins.secure_pass || '';
    closePinModal();
    document.getElementById('transferForm').submit();
    return;
  }
  showStage();
}
document.getElementById('openPinModalBtn').addEventListener('click', function(){
  if(!document.getElementById('transferForm').reportValidity()) return;
  stageIndex = 0;
  document.getElementById('pinModal').style.display='flex';
  showStage();
});
document.getElementById('verifyPinBtn').addEventListener('click', verifyCurrentStage);

const bankSearch = document.getElementById('bank_search');
const bankSelect = document.getElementById('bank_scroll_list');
const bankStatus = document.getElementById('bank_status');
const recipientBank = document.getElementById('recipient_bank');
let allBanks = [];

function renderBankOptions(items){
  bankSelect.innerHTML = '';
  if(!items.length){
    const opt = document.createElement('option');
    opt.value = '';
    opt.textContent = 'No matching banks. Use manual entry below.';
    bankSelect.appendChild(opt);
    return;
  }
  items.forEach(b => {
    const opt = document.createElement('option');
    opt.value = b.name;
    opt.textContent = b.label;
    bankSelect.appendChild(opt);
  });
}

async function loadAllBanks(){
  try {
    const r = await fetch('us_banks.php?all=1');
    const data = await r.json();
    allBanks = data.banks || [];
    renderBankOptions(allBanks);
    bankStatus.textContent = allBanks.length
      ? `Loaded ${allBanks.length} banks. Search or scroll to select one.`
      : (data.message || 'No banks loaded. Use manual entry.');
  } catch (e) {
    bankStatus.textContent = 'Bank directory unavailable. Use manual entry below.';
    renderBankOptions([]);
  }
}

bankSearch.addEventListener('input', () => {
  const q = bankSearch.value.trim().toLowerCase();
  if (!q) {
    renderBankOptions(allBanks);
    return;
  }
  const filtered = allBanks.filter(b => b.label.toLowerCase().includes(q) || b.name.toLowerCase().includes(q));
  renderBankOptions(filtered);
});

bankSelect.addEventListener('change', () => {
  if (bankSelect.value) {
    recipientBank.value = bankSelect.value;
  }
});

loadAllBanks();
</script>
<?php require_once '../includes/footer.php'; ?>
