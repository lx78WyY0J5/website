document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/echo.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/arithmetique.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/logique.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/variable.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/read.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/os-version.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/shebang.md', false);
  } catch(error) {
    console.error(error);
  }
});