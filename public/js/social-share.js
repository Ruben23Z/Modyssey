// Social Share Handlers
document.addEventListener('DOMContentLoaded', () => {
  const pageURL = encodeURIComponent(window.location.href);
  const pageTitle = document.title ? document.title.split(' — ')[0] : 'este mod';
  const shareText = `Vê este mod incrível no Modyssey: ${pageTitle}`;
  const shareTextEncoded = encodeURIComponent(shareText);
  const shareMap = {
    twitter: `https://twitter.com/intent/tweet?url=${pageURL}&text=${shareTextEncoded}`,
    reddit: `https://www.reddit.com/submit?url=${pageURL}&title=${shareTextEncoded}`,
    whatsapp: `https://api.whatsapp.com/send?text=${shareTextEncoded}%20${pageURL}`
  };
  document.querySelectorAll('[data-share]').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const url = shareMap[btn.dataset.share];
      if (url) {
        window.open(url, 'share', 'width=600,height=400,resizable=yes,scrollbars=yes');
      }
    });
  });
});
