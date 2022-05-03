<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="assets/css/syllabusstyle.css"/>
</head>
<body>
<footer>
    <div class="pagenum-container"><span class="pagenum"></span></div>
</footer>

<div class="udeha-syllabus-logo">

    <?php if(!is_null($logopath)): ?>
        <img src="<?php echo $logopath; ?>">
    <?php endif; ?>
    <div class="udeha-title-courseplan"><?php print_string('courseplan', 'format_udehauthoring') ?></div>
</div>

<?php echo $this->get_presentation_content(); ?>

<div class="udeha-page-end"></div>

<div class="udeha-page-block">
    <h3><?php print_string('coursedescription', 'format_udehauthoring'); ?></h3>
    <?php echo $this->get_desc_content(); ?>
</div>

<div class="udeha-page-block">
    <h3><?php print_string('teachingobjectives', 'format_udehauthoring'); ?></h3>
    <?php echo $this->get_objectives_content(); ?>
</div>

<div class="udeha-page-block">
    <h3><?php print_string('courseplace', 'format_udehauthoring'); ?></h3>
    <?php echo $this->get_place_content(); ?>
</div>

<div class="udeha-page-block">
    <h3><?php print_string('modulescontent', 'format_udehauthoring'); ?></h3>
    <?php echo $this->get_modules_content(); ?>
</div>

<div class="udeha-page-block">
    <h3><?php print_string('evaluations', 'format_udehauthoring'); ?></h3>
    <?php echo $this->get_evaluations_content(); ?>
</div>

<div class="udeha-page-block">
    <h3><?php print_string('extrainfo', 'format_udehauthoring'); ?></h3>
    <?php echo $this->get_extra_content(); ?>
</div>

</body>
</html>