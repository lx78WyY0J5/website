document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/man.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/cmd-parameters.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/alias.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/history.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/copy-paste.md', false);
  } catch(error) {
    console.error(error);
  }
});