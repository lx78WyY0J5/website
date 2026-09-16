document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/Github/Markdown/Listing.md', false);
    await addMarkdown('github/.github', 'profile/README.md', false);
    await addMarkdown('lx78WyY0J5', '1525e23e7a3502c71014a499394ee967', true);
  } catch(error) {
    console.error(error);
  }
});