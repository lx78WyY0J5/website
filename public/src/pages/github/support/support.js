document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'profile/URL.md', false);
    await addMarkdown('Altherneum/.github', 'SUPPORT.md', false);
  } catch(error) {
    console.error(error);
  }
});
