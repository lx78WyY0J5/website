document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/apt.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/pacman.md', false);
  } catch(error) {
    console.error(error);
  }
});