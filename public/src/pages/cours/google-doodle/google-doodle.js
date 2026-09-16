document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/Google/doodle.md', false);
  } catch(error) {
    console.error(error);
  }
});