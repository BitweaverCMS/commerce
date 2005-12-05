{if $sideboxMusicGenres}
	{bitmodule title=$moduleTitle name="bc_musicgenres"}
		$content = "";
		$content.= zen_draw_form('music_genres', zen_href_link(FILENAME_DEFAULT, '', 'NONSSL', false), 'get');
		$content .= zen_draw_pull_down_menu('music_genre_id', $music_genres_array, (isset($_GET['music_genre_id']) ? $_GET['music_genre_id'] : ''), 'onchange="this.form.submit();" size="' . MAX_MUSIC_GENRES_LIST . '" style="width: 100%"') . zen_hide_session_id() .zen_draw_hidden_field('typefilter', 'music_genre');
		$content .= zen_draw_hidden_field('main_page', FILENAME_DEFAULT) . '</form>';
	{/bitmodule}
{/if}
