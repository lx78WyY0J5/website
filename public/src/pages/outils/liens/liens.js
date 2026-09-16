document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/Liens.md', false);
  } catch(error) {
    console.error(error);
  }
});
