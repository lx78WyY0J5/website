document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/OS/Windows/Windows/shortcut.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Windows/Windows/learning.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Windows/Windows/console.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Windows/Windows/wsl.md', false);
  } catch(error) {
    console.error(error);
  }
});
