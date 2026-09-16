document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/Network/fibre.md', false);
  } catch(error) {
    console.error(error);
  }
});
