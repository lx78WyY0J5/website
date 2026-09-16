document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/Code/Web/CSS/learning.md', false);
    await addMarkdown('Altherneum/.github', 'note/Code/Web/CSS/media-queries.md', false);
    await addMarkdown('Altherneum/.github', 'note/Code/Web/CSS/liste.md', false);
  } catch(error) {
    console.error(error);
  }
});
