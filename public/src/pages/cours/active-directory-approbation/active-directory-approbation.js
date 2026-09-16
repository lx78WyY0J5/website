document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/OS/Windows/Active-Directory/approbation.md', false);
  } catch(error) {
    console.error(error);
  }
});
