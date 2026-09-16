document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/code-server-apache.md', false);
  } catch(error) {
    console.error(error);
  }
});