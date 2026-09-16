document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'profile/URL.md', false);
    await addMarkdown('Altherneum/.github', 'CODE_OF_CONDUCT.md', false);
  } catch(error) {
    console.error(error);
  }
});