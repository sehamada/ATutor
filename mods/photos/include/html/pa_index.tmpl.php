<div>
	<!-- Photo album options and page numbers -->
	<div class="album_panel">
	<?php if($this->num_rows > AT_PA_ALBUMS_PER_PAGE){?>

		<div class="topbar">
			<!-- page numbers -->
			<div class="paginator">
				<?php print_paginator($this->page, $this->num_rows, 'type='.$this->type, AT_PA_ALBUMS_PER_PAGE, AT_PA_PAGE_WINDOW);  ?>
			</div>
		</div>

	<?php  } ?>
		<!-- loop through this -->
		<?php if(!empty($this->albums)): 
			$pa = new PhotoAlbum();
		?>
		<?php foreach($this->albums as $index=>$row): ?>
		<div class="album">
			<!-- TODO: If photo is not presense, print another image? -->
			<div class="image">
			<?php 
			$photo_info = $pa->getPhotoInfo($row['photo_id']); 
			if (!empty($photo_info)):
			?>
			<a><a href="<?php echo AT_PA_BASENAME.'albums.php?id='.$row['id'];?>"><img src="<?php echo AT_PA_BASENAME.'get_photo.php?aid='.$row['id'].SEP.'pid='.$row['photo_id'].SEP.'ph='.getPhotoFilePath($photo_info['id'], '', $photo_info['created_date']);?>" title="<?php echo htmlentities_utf82($photo_info['description']); ?>" alt="<?php echo htmlentities_utf82($photo_info['alt_text']); ?>" border="0"/></a>
			<?php endif; //image ?>
			</div>
			<div class="info">
				<h4><a href="<?php echo AT_PA_BASENAME.'albums.php?id='.$row['id'];?>"><?php echo htmlentities_utf82($row['name']); ?></a></h4>
				<p><?php echo htmlentities_utf82($row['description']); ?></p>
				<p><?php echo _AT('location').': '.htmlentities_utf82($row['location']); ?></p>
				<span>
				<p><?php echo _AT('last_updated', AT_date(_AT('forum_date_format'), $row['last_updated'], AT_DATE_MYSQL_DATETIME));?></p>
				<p><?php echo _AT('created').': '.AT_date(_AT('forum_date_format'), $row['created_date'], AT_DATE_MYSQL_DATETIME); ?></p>
				</span><br/>
				<p><a href="<?php echo AT_PA_BASENAME;?>edit_album.php?id=<?php echo $row['id'];?>"><?php echo _AT('edit'); ?></a> | <a href="<?php echo AT_PA_BASENAME;?>delete_album.php?id=<?php echo $row['id'];?>"><?php echo _AT('delete');?></a></p>
			</div>
		</div>
		<?php endforeach; ?>
		<?php else: ?>
		<div class="album">
			<p><?php echo _AT('pa_no_album'); ?></p>
		</div>
		<?php endif; ?>
		<!-- end loop -->
		<!-- page numbers -->
		<?php if($this->num_rows > AT_PA_ALBUMS_PER_PAGE){?>
		<div class="topbar">
			<div class="paginator">
				<?php print_paginator($this->page, $this->num_rows, 'type='.$this->type, AT_PA_ALBUMS_PER_PAGE, AT_PA_PAGE_WINDOW);  ?>
			</div>
		</div>
		<?php  } ?>
	</div>	
</div>