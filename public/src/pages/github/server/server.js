document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'profile/URL.md', false);
    await addMarkdown('Altherneum/server', 'README.md', false);
  } catch(error) {
    console.error(error);
  }
});
