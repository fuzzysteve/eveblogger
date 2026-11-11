{include file="header.tpl" title="Admin - Eve Bloggers"}
{if !$loggedin}
<h1 class="h4">Admin</h1>
{if isset($error)}<div class="alert alert-danger">{$error}</div>{/if}
<form method="post" action="/admin.php" class="col-md-4">
  <label for="password" class="form-label">Password</label>
  <input type="password" name="password" id="password" class="form-control mb-3" autocomplete="current-password" required autofocus>
  <button class="btn btn-primary">Log in</button>
</form>
{else}
<div class="d-flex align-items-center mb-3">
  <h1 class="h4 mb-0">Admin</h1>
  <form method="post" action="/admin.php" class="ms-auto">
    <input type="hidden" name="csrf" value="{$csrf}"><input type="hidden" name="action" value="logout"><input type="hidden" name="id" value="0">
    <button class="btn btn-sm btn-outline-secondary">Log out</button>
  </form>
</div>
{if $flash}<div class="alert alert-info">{$flash}</div>{/if}

{* One small POST form per button, so each action carries the CSRF token. *}
{function name=btn action='' id=0 label='' class='btn-outline-secondary' status=null}
<form method="post" action="/admin.php" class="d-inline">
  <input type="hidden" name="csrf" value="{$csrf}"><input type="hidden" name="action" value="{$action}"><input type="hidden" name="id" value="{$id}">
  {if $status !== null}<input type="hidden" name="status" value="{$status}">{/if}
  <button class="btn btn-sm {$class}">{$label}</button>
</form>
{/function}

<h2 class="h5 mt-4">Held feeds <span class="badge text-bg-{if $held}warning{else}secondary{/if}">{$held|count}</span></h2>
<p class="small text-body-secondary">The loader stops polling a blog when all its new posts link to a site the blog isn't known at, usually because the domain lapsed and the feed now redirects somewhere else.</p>
{foreach $held as $b}
  <div class="card mb-2"><div class="card-body py-2">
    <strong>#{$b.id} {$b.name}</strong> <span class="small text-body-secondary">since {$b.since} UTC</span><br>
    <span class="small">{$b.held_reason}</span><br>
    <span class="small text-body-secondary">Feed: {$b.feedurl}</span>
    <div class="mt-2">
      {call btn action='trust' id=$b.id label='Trust new host & resume' class='btn-outline-primary'}
      {call btn action='status' id=$b.id status=2 label='Retire' class='btn-outline-danger'}
      {call btn action='release' id=$b.id label='Release (re-check)'}
    </div>
  </div></div>
{foreachelse}
  <p class="small">None.</p>
{/foreach}

<h2 class="h5 mt-4">Pending submissions <span class="badge text-bg-{if $pending}primary{else}secondary{/if}">{$pending|count}</span></h2>
{foreach $pending as $b}
  <div class="card mb-2"><div class="card-body py-2">
    <strong>#{$b.id} {$b.name}</strong> by {$b.owner}<br>
    <span class="small">Site: {$b.url|default:'-'} · Feed: {$b.feedurl}</span>
    {if $b.description}<br><span class="small text-body-secondary">{$b.description}</span>{/if}
    <div class="mt-2">
      {call btn action='status' id=$b.id status=1 label='Approve' class='btn-outline-success'}
      {call btn action='status' id=$b.id status=-2 label='Reject' class='btn-outline-danger'}
    </div>
  </div></div>
{foreachelse}
  <p class="small">None.</p>
{/foreach}

<h2 class="h5 mt-4">Failing in the latest loader run <span class="badge text-bg-secondary">{$failing|count}</span></h2>
{if $failing}
<div class="table-responsive"><table class="table table-sm align-middle">
  <thead><tr><th>Blog</th><th>Error</th><th></th></tr></thead>
  <tbody>
  {foreach $failing as $f}
    <tr><td class="text-nowrap">#{$f.id} {$f.name}</td><td class="small">{$f.error}</td>
        <td class="text-end">{call btn action='status' id=$f.id status=2 label='Retire' class='btn-outline-danger'}</td></tr>
  {/foreach}
  </tbody>
</table></div>
{else}<p class="small">None.</p>{/if}

<h2 class="h5 mt-4">All blogs <span class="badge text-bg-secondary">{$blogs|count}</span></h2>
<div class="table-responsive"><table class="table table-sm table-hover align-middle">
  <thead><tr><th>#</th><th>Blog</th><th>Last post</th><th>Status</th></tr></thead>
  <tbody>
  {foreach $blogs as $b}
    <tr>
      <td>{$b.id}</td>
      <td>{if $b.url}<a href="{$b.url}" rel="noopener noreferrer">{$b.name}</a>{else}{$b.name}{/if}<br><span class="small text-body-secondary">{$b.feedurl}</span></td>
      <td class="text-nowrap">{$b.last_post|default:'-'}</td>
      <td>
        <form method="post" action="/admin.php" class="d-flex gap-1">
          <input type="hidden" name="csrf" value="{$csrf}"><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="{$b.id}">
          <select name="status" class="form-select form-select-sm" aria-label="Status for #{$b.id}">
            {foreach $statuses as $value => $label}<option value="{$value}"{if $value == $b.approved} selected{/if}>{$label}</option>{/foreach}
          </select>
          <button class="btn btn-sm btn-outline-secondary">Save</button>
        </form>
      </td>
    </tr>
  {/foreach}
  </tbody>
</table></div>
{/if}
{include file="footer.tpl"}
