document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/Github/Markdown/Learning.md', false);
    await addMarkdown('Altherneum/.github', 'note/Github/Markdown/Listing.md', false);
  } catch(error) {
    console.error(error);
  }
});