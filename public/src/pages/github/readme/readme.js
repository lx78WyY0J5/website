document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'profile/README.md', false);
    await addMarkdown('Altherneum/.github', 'profile/URL.md', false);
  } catch(error) {
    console.error(error);
  }
});