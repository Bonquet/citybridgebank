# Push the `work` rework commit to GitHub `main`

This container cannot reach GitHub from this environment (`CONNECT tunnel failed, response 403`), so pushing must be done from your local machine.

## 1) In your local clone, add this repo as a remote

```bash
git remote add origin https://github.com/Bonquet/citybridgebank.git
# If origin already exists:
# git remote set-url origin https://github.com/Bonquet/citybridgebank.git
```

## 2) Fetch and inspect

```bash
git fetch origin
git branch -a
```

## 3) If you already have the rework commit locally on `work`, push it to `main`

```bash
git checkout work
git push origin work:main
```

## 4) If you only have `main`, apply your rework files first, then commit and push

```bash
git add .
git commit -m "Apply rework"
git push origin main
```

## 5) Verify

```bash
git log --oneline -n 5
git status
```
