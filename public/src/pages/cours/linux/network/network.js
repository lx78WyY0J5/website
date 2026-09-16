document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/netstat.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/ps.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/UFW.md', false);
  } catch(error) {
    console.error(error);
  }
});
