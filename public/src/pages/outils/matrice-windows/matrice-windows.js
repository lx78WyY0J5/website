document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/OS/Windows/Scripts/matrice.bat.md', false);
  } catch(error) {
    console.error(error);
  }
});
