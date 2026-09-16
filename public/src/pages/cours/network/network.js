document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/Network/networks.md', false);
    await addMarkdown('Altherneum/.github', 'note/Network/OSI.md', false);
    await addMarkdown('Altherneum/.github', 'note/Network/cast.md', false);
  } catch(error) {
    console.error(error);
  }
});