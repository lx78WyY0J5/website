document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/PS1.md', false);
  } catch(error) {
    console.error(error);
  }
});
