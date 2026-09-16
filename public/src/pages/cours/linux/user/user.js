document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/who.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/whoami.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/user.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/sudo.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/password.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/faillock.md', false);
  } catch(error) {
    console.error(error);
  }
});