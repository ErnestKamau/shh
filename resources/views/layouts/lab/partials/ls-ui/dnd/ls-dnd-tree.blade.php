{{--
	ls-dnd-tree — nested tree with expand and HTML5 DnD visual states (demo).
--}}
@php
	$treeId = $treeId ?? 'ls-tree-'.uniqid();
@endphp
<ul class="ls-dnd-tree" id="{{ $treeId }}" data-ls-dnd-tree x-data="{ openChild: true }">
	<li>
		<div class="ls-dnd-tree-node" draggable="true">
			<button type="button" class="ls-dnd-tree-handle" aria-label="Drag"><i class="mdi mdi-menu"></i></button>
			<div class="ls-dnd-tree-body">
				<button type="button" class="ls-dnd-tree-toggle" @click="openChild = !openChild" x-text="openChild ? '−' : '+'"></button>
				<span class="ls-dnd-tree-title">Parent Node</span>
				<p class="ls-dnd-tree-sub">subtitle of parent node</p>
			</div>
		</div>
		<ul x-show="openChild">
			<li>
				<div class="ls-dnd-tree-node" draggable="true">
					<button type="button" class="ls-dnd-tree-handle" aria-label="Drag"><i class="mdi mdi-menu"></i></button>
					<div class="ls-dnd-tree-body">
						<p class="ls-dnd-tree-title mb-0">Child Node</p>
						<p class="ls-dnd-tree-sub">subtitle of child node</p>
					</div>
				</div>
			</li>
			<li>
				<div class="ls-dnd-tree-node" draggable="true">
					<button type="button" class="ls-dnd-tree-handle" aria-label="Drag"><i class="mdi mdi-menu"></i></button>
					<div class="ls-dnd-tree-body">
						<p class="ls-dnd-tree-title mb-0">Child Node B</p>
						<p class="ls-dnd-tree-sub">drop target uses dashed blue outline</p>
					</div>
				</div>
			</li>
		</ul>
	</li>
</ul>
