document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/OS/reverse-shell.md', false);
  } catch(error) {
    console.error(error);
  }
});
