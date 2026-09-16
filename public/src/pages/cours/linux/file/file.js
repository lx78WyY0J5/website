document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/directory.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/more.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/grep.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/tmp.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/lister.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/file.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/permission.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/tar.md', false);
  } catch(error) {
    console.error(error);
  }
});