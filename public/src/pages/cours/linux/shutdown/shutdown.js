document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/shutdown-reboot.md', false);
  } catch(error) {
    console.error(error);
  }
});