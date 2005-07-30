{if $sideboxLanguages}

{bitmodule title=$moduleTitle name="languages"}

{foreach item=langCode value=language from=$sideboxLanguages}
<a href="{$smarty.server.PHP_SELF}?language={$langCode}"><img src={$smarty.const.DIR_WS_LANGUAGES}{$language.directory}/images/$language.image" alt=""/> {$language.name}</a>
{/foreach}

{/bitmodule}

{/if}