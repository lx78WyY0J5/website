document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/Network/protocoles.md', false);
  } catch(error) {
    console.error(error);
  }
});