document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/Network/enterprise-network.md', false);
  } catch(error) {
    console.error(error);
  }
});
