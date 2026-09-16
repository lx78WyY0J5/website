document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/MOTD.md', false);
  } catch(error) {
    console.error(error);
  }
});