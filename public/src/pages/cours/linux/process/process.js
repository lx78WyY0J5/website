document.addEventListener('DOMContentLoaded', async function() {
  try {
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/free.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/top.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/uptime.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/Jobs.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/screen.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/kill.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/PIDof.md', false);
    await addMarkdown('Altherneum/.github', 'note/OS/Linux/systemctl.md', false);
  } catch(error) {
    console.error(error);
  }
});