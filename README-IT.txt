U12 TOURNAMENT 2026 - GITHUB PAGES PACKAGE

PACKAGE CONTENTS
- Public English pages: index.html, schedule.html, live.html, standings.html, stats.html
- Shared repository files: data/schedule.json and data/results.json
- Unlisted administration page: admin-results-7c4e91b2.html
- No PHP, server or database is required.

DEPLOYMENT
1. Create a GitHub repository and upload the CONTENTS of this folder to the repository root.
2. In repository Settings > Pages, choose deployment from the main branch and root folder.
3. Wait until GitHub reports the Pages deployment as active.
4. Open index.html through the GitHub Pages address.
5. Keep the administration filename private. It is not linked by any public page and contains a noindex directive.

ADMINISTRATION
Open the unlisted page directly:
  admin-results-7c4e91b2.html
Enter:
- GitHub owner
- repository name
- branch, normally main
- a fine-grained personal access token restricted to this repository with repository Contents permission set to Read and write
Click Connect and load. Select a game, edit score, status, pitchers, batters and MVP, then click Save game in working copy. Click Publish to GitHub to commit data/results.json.

TOKEN SAFETY
- Never put the token in source files, config files, data/results.json, commits or screenshots.
- The page keeps the token only in the current page memory. It is not saved to localStorage or the repository.
- Revoke and replace the token if it is exposed.
- The secret URL alone is not authentication. Anyone who knows it can see the editor, but publishing still requires a repository token.

MANUAL FALLBACK
The admin page can Export results.json. If API publication is unavailable, upload the exported file to data/results.json in GitHub and commit it. Import JSON can restore a previous working copy.

RANKING
Only completed Group A and Group B games with two integer scores and no tie are counted. Order: overall winning percentage; mini-table winning percentage among tied teams; mini-table run differential; overall run differential; runs scored; team name. Official tournament rules take precedence.

FINALS
Public pages calculate final pairings automatically from the current standings:
Game 31: 6th A vs 6th B
Game 32: 5th A vs 5th B
Game 33: 4th A vs 4th B
Game 34: 3rd A vs 3rd B
Game 35: 2nd A vs 2nd B
Game 36: 1st A vs 1st B

NOTES
- The Live page refreshes every 20 seconds.
- GitHub Pages deployment may not be instantaneous after a commit.
- Name + Team identifies an athlete in aggregate statistics. Use consistent spelling.
- RL is stored and summed exactly as entered.
