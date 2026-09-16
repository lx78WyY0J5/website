document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/OS/forkBomb.md', false);
  } catch(error) {
    console.error(error);
  }
});
