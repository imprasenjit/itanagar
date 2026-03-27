# Onboarding Guide: Copilot Agents Setup

## Step 1: Install Extensions
- Install **GitHub Copilot** and **GitHub Copilot Chat** in VS Code.

## Step 2: Enable Agents
- Ensure your org/account has **Copilot Agents** enabled.

## Step 3: Project Setup
- Create `.copilot/` folder in your project root.
- Add `skills.json`, `agent.json`, and `skills.md`.

## Step 4: Documentation
- Create `docs/` folder.
- Add `prompt-library.md`, `workflow-cheatsheet.md`, and `onboarding-guide.md`.

## Step 5: Usage
- Use inline Copilot completions for small edits.
- Use `@project-assistant` in Copilot Chat for orchestrated tasks.
- Refer to `skills.md` and `prompt-library.md` for guidance.

## Step 6: Best Practices
- Keep `.copilot/` versioned in Git.
- Share `docs/` with teammates.
- Link `docs/` from your main `README.md`.
