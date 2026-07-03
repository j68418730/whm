<?php $engine = new \Services\WebsiteBuilderEngine(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Editor: <?php echo htmlspecialchars($page->title); ?> - <?php echo htmlspecialchars($site->name); ?></title>
<link rel="stylesheet" href="/theme/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/plugins/website-builder/css/editor.css">
</head>
<body data-page-id="<?php echo $page->id; ?>" data-site-id="<?php echo $site->id; ?>">
<div class="editor-toolbar">
<div class="brand"><i class="bi bi-pencil-square"></i> <?php echo htmlspecialchars($page->title); ?></div>
<div class="actions">
<span style="display:flex;gap:4px;padding:2px;background:rgba(0,0,0,.2);border-radius:6px;align-items:center">
<button class="device-btn active" onclick="setDevice('desktop',this)" title="Desktop"><i class="bi bi-tv"></i></button>
<button class="device-btn" onclick="setDevice('tablet',this)" title="Tablet (768px)"><i class="bi bi-tablet"></i></button>
<button class="device-btn" onclick="setDevice('mobile',this)" title="Mobile (375px)"><i class="bi bi-phone"></i></button>
</span>
<button onclick="togglePreview(this)" title="Split preview"><i class="bi bi-layout-split"></i> Split</button>
<button onclick="savePage()" class="btn-save"><i class="bi bi-check-lg"></i> Save</button>
<a href="/user/websites/<?php echo $site->id; ?>/preview/<?php echo $page->id; ?>" target="_blank"><i class="bi bi-eye"></i> Preview</a>
<a href="/user/websites/<?php echo $site->id; ?>" style="color:#f87171"><i class="bi bi-x-lg"></i> Close</a>
</div>
</div>
<div class="editor-layout">
<div class="editor-sidebar">
<?php $cats = ['structure'=>'Structure','content'=>'Content','media'=>'Media','components'=>'Components','integrations'=>'Integrations','advanced'=>'Advanced'];
foreach ($cats as $catKey => $catName):
if (!isset($categorized[$catKey])) continue; ?>
<div class="block-category">
<h5><?php echo $catName; ?></h5>
<?php foreach ($categorized[$catKey] as $bk => $bv): ?>
<div class="block-item" draggable="true" data-type="<?php echo $bk; ?>" data-fields='<?php echo htmlspecialchars(json_encode($bv['fields'] ?? [])); ?>'>
<i class="<?php echo $bv['icon'] ?? 'fa-solid fa-cube'; ?>" style="width:16px;text-align:center;color:var(--accent)"></i>
<?php echo htmlspecialchars($bv['name'] ?? $bk); ?>
</div>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
</div>
<div class="editor-main" id="editorMain">
<div class="editor-canvas desktop" id="editorCanvas">
<?php $zones = ['header', 'content', 'footer'];
foreach ($zones as $zone):
$zoneBlocks = array_filter($page->blocks, fn($b) => ($b->zone ?? 'content') === $zone); ?>
<div class="wb-zone" data-zone="<?php echo $zone; ?>">
<?php if ($zone === 'content' || !empty($zoneBlocks)): ?>
<?php if (empty($zoneBlocks)): ?>
<div class="empty-zone">Drop blocks here</div>
<?php endif; ?>
<?php foreach ($zoneBlocks as $b): ?>
<div class="editor-block" data-id="<?php echo $b->id; ?>" data-type="<?php echo $b->type; ?>" data-content='<?php echo htmlspecialchars(json_encode($b->content)); ?>' data-settings='<?php echo htmlspecialchars(json_encode($b->settings_arr ?? [])); ?>'>
<div class="block-controls">
<button onclick="moveBlock(this, -1)" title="Move up"><i class="bi bi-chevron-up"></i></button>
<button onclick="moveBlock(this, 1)" title="Move down"><i class="bi bi-chevron-down"></i></button>
<button class="btn-del" onclick="deleteBlock(this)" title="Delete"><i class="bi bi-trash"></i></button>
</div>
<?php echo $engine->renderBlock($b); ?>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<iframe id="previewFrame" src="/user/websites/<?php echo $site->id; ?>/preview/<?php echo $page->id; ?>"></iframe>
</div>
<div class="editor-right-panel" id="settingsPanel">
<h5>Block Settings</h5>
<p style="color:#64748b;font-size:12px">Select a block to edit its settings.</p>
</div>
</div>
<script src="/plugins/website-builder/js/editor.js"></script>
</body>
</html>
