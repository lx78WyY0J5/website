document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/0x0.st.md', false);
  } catch(error) {
    console.error(error);
  }
});