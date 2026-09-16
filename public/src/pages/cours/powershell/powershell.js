document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/OS/Windows/PowerShell/powershell.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Windows/PowerShell/cmd.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Windows/PowerShell/variables.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Windows/PowerShell/arithmetique.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Windows/PowerShell/logique.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Windows/PowerShell/powershell-AD.md', false);
  } catch(error) {
    console.error(error);
  }
});