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

<?php if (!empty($this->get_desc_content())): ?>
<div class="udeha-page-block">
    <h3><?php print_string('coursedescription', 'format_udehauthoring'); ?></h3>
    <?php echo $this->get_desc_content(); ?>
</div>
<?php endif; ?>

<?php if (!empty($this->get_objectives_content())): ?>
<div class="udeha-page-block">
    <h3><?php print_string('teachingobjectives', 'format_udehauthoring'); ?></h3>
    <?php echo $this->get_objectives_content(); ?>
</div>
<?php endif; ?>

<div class="udeha-page-block">
    <?php if (!empty($this->get_objectives_content())): ?>
        <h3><?php print_string('syllabustitle_problematic', 'format_udehauthoring'); ?></h3>
        <?php echo $this->get_problematic_content(); ?>
    <?php endif; ?>

    <?php if (!empty($this->get_place_content())): ?>
        <h3><?php print_string('courseplace', 'format_udehauthoring'); ?></h3>
        <?php echo $this->get_place_content(); ?>
    <?php endif; ?>

    <?php if (!empty($this->get_method_content())): ?>
        <h3><?php print_string('syllabustitle_method', 'format_udehauthoring'); ?></h3>
        <?php echo $this->get_method_content(); ?>
    <?php endif; ?>
</div>

<?php if (!empty($this->get_modules_content())): ?>
<div class="udeha-page-block">
    <h3><?php print_string('modulescontent', 'format_udehauthoring'); ?></h3>
    <?php echo $this->get_modules_content(); ?>
</div>
<?php endif; ?>

<?php if (!empty($this->get_evaluations_content())): ?>
<div class="udeha-page-block">
    <h3><?php print_string('evaluations', 'format_udehauthoring'); ?></h3>
    <?php echo $this->get_evaluations_content(); ?>
</div>
<?php endif; ?>

<?php if (!empty($this->get_extra_content())): ?>
<div class="udeha-page-block">
    <h3><?php print_string('extrainfo', 'format_udehauthoring'); ?></h3>
    <?php echo $this->get_extra_content(); ?>
</div>
<?php endif; ?>

</body>
</html>