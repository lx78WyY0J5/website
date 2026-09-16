document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/OS/Windows/Windows/hyper-v.md', false);
  } catch(error) {
    console.error(error);
  }
});
