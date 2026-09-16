document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/IA/learning.md', false);
  } catch(error) {
    console.error(error);
  }
});
