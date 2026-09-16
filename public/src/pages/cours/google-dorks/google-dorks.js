document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/Google/dorking.md', false);
  } catch(error) {
    console.error(error);
  }
});
