document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/README.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/introduction.md', false);
  } catch(error) {
    console.error(error);
  }
});
