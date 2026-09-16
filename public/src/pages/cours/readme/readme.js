document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/README.md', false);
  } catch(error) {
    console.error(error);
  }
});