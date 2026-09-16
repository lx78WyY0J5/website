document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/Code/Web/SQL/learning.md', false);
  } catch(error) {
    console.error(error);
  }
});