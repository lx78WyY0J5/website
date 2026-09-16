document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/remote.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/ssh.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/scp.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/wget.md', false);
  } catch(error) {
    console.error(error);
  }
});
