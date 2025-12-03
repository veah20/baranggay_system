# 🔧 Railway Deployment - Command Reference

**Copy & paste these commands in PowerShell to deploy your system.**

---

## 📋 Prerequisites

- [ ] Git installed on your computer
- [ ] GitHub account created
- [ ] Railway account created
- [ ] Your Barangay Information System ready

---

## 🚀 Deployment Commands

### Step 1: Navigate to Your Project

```powershell
# Copy & Paste This
cd C:\xampp\htdocs\BarangayInformationResidentSystem
```

### Step 2: Initialize Git Repository

```powershell
# Initialize Git
git init

# Verify Git is initialized (should show "On branch master")
git status
```

### Step 3: Add All Files to Git

```powershell
# Add all files
git add .

# Verify files are staged (should show many files in green)
git status
```

### Step 4: Create First Commit

```powershell
# Create your first commit
git commit -m "Initial commit: BIRS ready for Railway deployment"

# Verify commit was created
git log --oneline -1
```

### Step 5: Add GitHub Remote

Replace `YOUR_USERNAME` with your actual GitHub username:

```powershell
# Add GitHub as remote repository
git remote add origin https://github.com/YOUR_USERNAME/barangay-information-system.git

# Verify remote was added
git remote -v
```

### Step 6: Rename Branch to Main

```powershell
# Rename current branch to main
git branch -M main

# Verify branch name changed
git branch -a
```

### Step 7: Push to GitHub

```powershell
# Push code to GitHub (main branch)
git push -u origin main

# After this completes, your code is on GitHub!
# You can now go to GitHub.com to see your repository
```

---

## 🔄 Update Commands (After Initial Deployment)

### When You Make Changes Locally

```powershell
# After making changes to PHP files:

# 1. Check what changed
git status

# 2. Stage changes
git add .

# 3. Commit changes with description
git commit -m "Description of your changes"

# Examples:
# git commit -m "Add new report feature"
# git commit -m "Fix database connection"
# git commit -m "Update styling"

# 4. Push to GitHub (Railway auto-deploys)
git push origin main

# Done! Your changes are automatically deployed to Railway
```

---

## 📊 Git Status Commands

### Check Your Git Status

```powershell
# See current branch and staged files
git status

# See commit history
git log --oneline

# See remote repositories
git remote -v

# See all branches
git branch -a

# See diff of changes
git diff
```

---

## 🔍 Useful Git Commands

### View Your Changes

```powershell
# See what changed in a specific file
git diff filename.php

# See summary of changes
git diff --stat

# See only new files
git status --short
```

### Undo Changes

```powershell
# Undo changes to a file (before committing)
git checkout filename.php

# Undo staged changes (before committing)
git reset filename.php

# Undo last commit (keep changes)
git reset --soft HEAD~1

# View previous version of file
git show HEAD:filename.php
```

### Fix Mistakes

```powershell
# Fix last commit message (if you made a typo)
git commit --amend -m "Corrected message"

# View previous commits
git log -5

# Go back to a previous commit
git checkout <commit-hash>

# Revert a specific commit
git revert <commit-hash>
```

---

## 🌐 GitHub Commands

### First Time Setup (If Git Is New)

```powershell
# Configure Git with your GitHub info (one time only)
git config --global user.name "Your Name"
git config --global user.email "your.email@github.com"

# Verify configuration
git config --global user.name
git config --global user.email
```

### Create New GitHub Repository Steps

```powershell
# Manual steps (cannot be automated):

# 1. Go to https://github.com/new
# 2. Repository name: barangay-information-system
# 3. Click "Create repository"
# 4. Copy the repository URL (looks like: https://github.com/USERNAME/barangay-information-system.git)
# 5. Use that URL in your git commands below

# Then run these commands:
git init
git add .
git commit -m "Initial commit"
git remote add origin [YOUR_REPOSITORY_URL]
git branch -M main
git push -u origin main
```

---

## 🔗 Testing Your GitHub Connection

```powershell
# Test SSH connection (if using SSH keys)
ssh -T git@github.com

# Test HTTPS connection
git remote -v

# Try a test push
git push origin main --dry-run
```

---

## 📱 Terminal Tips

### Copy & Paste in PowerShell

```
# How to copy output:
1. Select text in terminal
2. Right-click to copy
3. In another window, right-click to paste

# How to select all:
Ctrl + A

# How to clear screen:
Clear-Host

# Or just type:
cls
```

### Common PowerShell Commands

```powershell
# List files in current directory
Get-ChildItem

# Or shorter:
ls

# Change directory
cd path\to\directory

# Go up one directory
cd ..

# Go to home directory
cd ~

# Print current directory path
Get-Location

# Create new file
New-Item filename.txt

# View file contents
Get-Content filename.txt
```

---

## 🎯 Quick Deploy Script

**Save this as `deploy.ps1` and run it:**

```powershell
# deploy.ps1
# Quick deployment script for PowerShell

# Navigate to project
Set-Location "C:\xampp\htdocs\BarangayInformationResidentSystem"

# Check Git status
Write-Host "Current Git Status:"
git status

# Add all files
Write-Host "Adding files..."
git add .

# Commit with timestamp
$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
git commit -m "Deployment: $timestamp"

# Push to GitHub
Write-Host "Pushing to GitHub..."
git push origin main

Write-Host "✅ Deployment complete!"
Write-Host "Check GitHub: https://github.com/YOUR_USERNAME/barangay-information-system"
Write-Host "App will auto-deploy on Railway in 2-3 minutes"
```

**To use it:**
```powershell
# Save the above code as deploy.ps1
# Then run:
.\deploy.ps1
```

---

## 📚 Command Reference Table

| Command | Purpose |
|---------|---------|
| `git init` | Initialize Git repository |
| `git add .` | Stage all changes |
| `git commit -m "msg"` | Commit with message |
| `git remote add origin [url]` | Add GitHub remote |
| `git branch -M main` | Rename branch to main |
| `git push origin main` | Push to GitHub |
| `git pull origin main` | Pull from GitHub |
| `git log --oneline` | View commit history |
| `git status` | Check current status |
| `git diff` | See changes |
| `git reset filename` | Unstage file |
| `git checkout filename` | Undo changes |

---

## 🆘 Troubleshooting Commands

### If something goes wrong:

```powershell
# Check Git configuration
git config --global --list

# Check remote URL
git remote -v

# See detailed commit info
git log -p

# See who made changes
git log --oneline --all --graph

# Check if SSH keys work
ssh -T git@github.com

# Clear Git cache (if having file permission issues)
git rm --cached -r .
git add .
git commit -m "Fix file permissions"
```

---

## 🔐 Security Note

**Never commit these files to GitHub:**

```powershell
# These should be in .gitignore:
.env
.env.local
config/database-production.php
uploads/*
logs/*

# Verify .gitignore is working:
git check-ignore -v filename
```

---

## 📱 All Commands Summary

### Complete Deployment in 7 Commands

```powershell
# 1. Navigate to project
cd C:\xampp\htdocs\BarangayInformationResidentSystem

# 2. Initialize Git
git init

# 3. Add all files
git add .

# 4. Commit
git commit -m "Initial commit: BIRS ready for Railway deployment"

# 5. Add GitHub remote (replace YOUR_USERNAME)
git remote add origin https://github.com/YOUR_USERNAME/barangay-information-system.git

# 6. Rename branch
git branch -M main

# 7. Push to GitHub
git push -u origin main
```

**That's it! Your code is now on GitHub and Railway will deploy it automatically.**

---

## 🎯 Next Steps After Running Commands

1. ✅ Code is on GitHub
2. Go to GitHub and verify your repository has all files
3. Go to railway.app and create project from GitHub
4. Set environment variables in Railway
5. Deploy database
6. Test your live app

---

## 💡 Pro Tips

```powershell
# Create alias for faster commands
Set-Alias gst "git status"
Set-Alias gadd "git add ."
Set-Alias gcom "git commit -m"

# View formatted log
git log --oneline --graph --all

# Amend last commit (if you made typo)
git commit --amend --no-edit

# See what will be pushed
git push origin main --dry-run
```

---

## 📞 Getting Help

If a command fails:

```powershell
# Get help for any git command
git help [command]

# Examples:
git help add
git help commit
git help push

# Or search online: "git [command] error"
```

---

**Version**: 1.0  
**Date**: December 3, 2025

👉 **Ready to deploy? Follow the 7 commands above!** 👈
