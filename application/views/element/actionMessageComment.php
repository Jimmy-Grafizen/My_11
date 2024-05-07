<?php if ($this->session->userdata('cmessage') || $this->session->flashdata('cmessage')) { ?>
    <div id="msgID" class="SuccessMsgBox success">
        <ul>
            <li>
                <?php
                echo $this->session->userdata('cmessage');
                echo $this->session->flashdata('cmessage');
                $this->session->unset_userdata('cmessage');
                ?>
            </li>
            <!--<li class="close-bt"></li>-->
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
