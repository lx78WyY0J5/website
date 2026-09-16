document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/Network/ip.md', false);
    await addMarkdown('Altherneum/.github', 'note/Code/Web/Binaire/ip.md', false);
  } catch(error) {
    console.error(error);
  }
});