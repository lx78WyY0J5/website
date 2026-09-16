document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/LVM.md', false);
  } catch(error) {
    console.error(error);
  }
});
