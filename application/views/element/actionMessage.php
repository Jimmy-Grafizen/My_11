<?php if (validation_errors() || $this->session->userdata('message') || $this->session->flashdata('message')) { ?>
    <div class="alert alert-block alert-danger fade in">
        <button data-dismiss="alert" class="close close-sm" type="button">
            <i class="fa fa-times"></i>
        </button>
        <?php
        echo validation_errors();
        echo $this->session->userdata('message');
        echo $this->session->flashdata('message');
        $this->session->unset_userdata('message');
        ?>
    </div>
<?php } ?>

<?php if ($this->session->userdata('smessage') || $this->session->flashdata('smessage')) { ?>
    <div class="alert alert-success fade in">
        <button data-dismiss="alert" class="close close-sm" type="button">
            <i class="fa fa-times"></i>
        </button>
        <?php
        echo $this->session->userdata('smessage');
        echo $this->session->flashdata('smessage');
        $this->session->unset_userdata('smessage');
        ?>
    </div>
<?php } ?>