/**
 * Strip HTML tags from a string.
 *
 * @param {string} value Raw HTML string.
 * @return {string}
 */
function stripHtml(value) {
  return String(value || '').replace(/<[^>]*>/g, '');
}

/**
 * Build a player episode object from a WordPress REST post payload.
 *
 * @param {object} post REST API post object.
 * @return {object|null}
 */
export function parseEpisodeFromRestPost(post) {
  const audioUrl = post.audio_file || post.acf?.audio_file || '';
  if (!audioUrl) {
    return null;
  }

  return {
    id: post.id,
    audioUrl,
    title: post.title?.rendered || '',
    description: stripHtml(post.excerpt?.rendered),
    publishDate: Math.floor(new Date(post.date).getTime() / 1000),
    featuredImage: post._embedded?.['wp:featuredmedia']?.[0]?.source_url || null,
    link: post.link || '',
  };
}

/**
 * Add episodes that are not already present in the playlist.
 *
 * @param {object[]} episodes Episode objects to merge.
 * @param {object[]} playlist Current playlist.
 * @param {function(object): void} addEpisode Callback that appends a new episode.
 * @return {{addedEpisodes: object[], firstNewEpisode: object|null}}
 */
export function mergeEpisodesIntoPlaylist(episodes, playlist, addEpisode) {
  const addedEpisodes = [];
  let firstNewEpisode = null;

  episodes.forEach((episode) => {
    if (!episode?.audioUrl) {
      return;
    }

    const exists = playlist.some((item) => item.id === episode.id);
    if (exists) {
      return;
    }

    addEpisode(episode);
    addedEpisodes.push(episode);

    if (!firstNewEpisode) {
      firstNewEpisode = episode;
    }
  });

  return { addedEpisodes, firstNewEpisode };
}
