{strip}
{bitmodule title=$moduleTitle name="bc_musicgenresselect"}
	<form name="musicgenres">
			{html_options options=$box_genres_array selected=$smarty.get.music_genre_id name="music_genre_id" onchange="this.form.submit();"}
			Genre
	</form>
	{/bitmodule}
{/strip}
