<?php if (validation_errors() || $this->session->userdata('message_t3') || $this->session->flashdata('message_t3')) { ?>
    <div id="msgID" class="ActionMsgBox error">
        <ul>
            <li>
                <?php
                echo validation_errors();
                echo $this->session->userdata('message_t3');
                echo $this->session->flashdata('message_t3');
                $this->session->unset_userdata('message_t3');
                ?>
            </li>
            <?php
            if ($this->uri->segment(1) <> 'admin') {
                ?>
                <li class="close-bt"></li>
            <?php } ?>
        </ul>

        <script>
            $(document).ready(function() {
                $('.close-bt').click(function() {
                    $("#msgID").fadeOut();
                })
            })
        </script>
    </div>
<?php } ?>

<?php if ($this->session->userdata('smessage_t3') || $this->session->flashdata('smessage_t3')) { ?>
    <div id="msgID" class="SuccessMsgBox success">
        <ul>
            <li>
                <?php
                echo $this->session->userdata('smessage_t3');
                echo $this->session->flashdata('smessage_t3');
                $this->session->unset_userdata('smessage_t3');
                ?>
            </li>
            <?php
            if ($this->uri->segment(1) <> 'admin') {
                ?>
                <li class="close-bt"></li>
            <?php } ?>
        </ul>

        <script>
            $(document).ready(function() {
                $('.close-bt').click(function() {
                    $("#msgID").fadeOut();
                })
            })
        </script>
    </div>
<?php } ?>
