document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/Code/Web/HTML/learning.md', false);
    await addMarkdown('Altherneum/.github', 'note/Code/Web/HTML/liste.md', false);
    await addMarkdown('Altherneum/.github', 'note/Code/Web/CSS/learning.md', false);
    await addMarkdown('Altherneum/.github', 'note/Code/Web/CSS/media-queries.md', false);
    await addMarkdown('Altherneum/.github', 'note/Code/Web/CSS/liste.md', false);
    await addMarkdown('Altherneum/.github', 'note/Code/Web/HTML/boilerplate.md', false);
    await addMarkdown('Altherneum/.github', 'note/Code/Web/SQL/learning.md', false);
    await addMarkdown('Altherneum/.github', 'note/Code/Web/JS/learning.md', false);
    await addMarkdown('Altherneum/.github', 'note/Code/Web/JS/variables.md', false);
    await addMarkdown('Altherneum/.github', 'note/Code/Web/JS/operateurs.md', false);
    await addMarkdown('Altherneum/.github', 'note/Code/Web/JS/function.md', false);
    await addMarkdown('Altherneum/.github', 'note/Code/Web/JS/conditions.md', false);
    await addMarkdown('Altherneum/.github', 'note/Code/Web/JS/boucles.md', false);
    await addMarkdown('Altherneum/.github', 'note/Code/Web/JS/wtfjs.md', false);
  } catch(error) {
    console.error(error);
  }
});
