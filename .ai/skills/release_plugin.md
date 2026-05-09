# Skill: Release Plugin

This skill outlines the standard operating procedure for releasing a new version of the **NHR Core Contributions** plugin.

## Versioning Definitions
- **Major Release**: Increment the second digit (0.1.0 bump). Example: `1.0.0` -> `1.1.0`.
- **Minor Release**: Increment the third digit (0.0.1 bump). Example: `1.0.0` -> `1.0.1`.

## Prerequisites
- Ensure all tests pass.

## Step 0: Sync Local Branches
Ensure all local branches are up to date with the remote before starting.
```bash
git checkout main && git pull origin main
git checkout dev && git pull origin dev
git checkout staging && git pull origin staging
git checkout dev
```

## Step 1: Sync Branches
Merge the `staging` branch into `dev` to ensure all latest tested changes are included.
```bash
git checkout dev
git merge staging
```

## Step 2: Version Bumping
1. **Main Plugin File**: Update the `Version:` header and constant in `nhrrob-core-contributions.php`.
2. **Readme File**: 
   - Update `Tested up to:` and `Stable tag:` in `readme.txt`.
   - Add a new entry under `== Changelog ==` following the existing format:
     ```text
     = X.Y.Z - DD/MM/YYYY =
     - Few minor bug fixes & improvements
     ```

## Step 3: Commit and PR
1. Commit the version bump:
   ```bash
   git add nhrrob-core-contributions.php readme.txt
   git commit -m "Chore: Bump version to X.Y.Z"
   ```
2. Push and create a Pull Request to `main`:
   ```bash
   git push origin dev
   # Create PR via GitHub CLI
   gh pr create --base main --head dev --title "Release X.Y.Z" --body "Release notes here"
   ```

## Step 4: Approval & Finalize Release
1. **Wait for Approval**: Do NOT proceed until the USER has manually reviewed the PR and explicitly said "yes" or "proceed".
2. **Merge PR**: Once approved, merge the PR into `main`.
3. Checkout `main` and pull changes:
   ```bash
   git checkout main
   git pull origin main
   ```
3. Create a git tag for the version:
   ```bash
   git tag -a vX.Y.Z -m "Release version X.Y.Z"
   git push origin vX.Y.Z
   ```
4. **Publish GitHub Release**:
   ```bash
   gh release create vX.Y.Z --title "X.Y.Z" --notes "Changelog description here"
   ```

## Step 5: Post-Release Sync
Ensure all development branches are in sync with the new release.
1. **Sync Dev**: Merge `main` back into `dev`.
   ```bash
   git checkout dev
   git merge main
   git push origin dev
   ```
2. **Sync Staging**: Merge `main` back into `staging`.
   ```bash
   git checkout staging
   git merge main
   git push origin staging
   ```
3. **Return to Dev**:
   ```bash
   git checkout dev
   ```
