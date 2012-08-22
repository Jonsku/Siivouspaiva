      <form id="email-login-form" method="POST" action="<?php echo $config['paths']['base_url']; ?>/data.php?query=login" class="form-vertical well">
        <fieldset>
          <input type="hidden" name="a" value="login"/>
          <input type="hidden" name="type" value="email"/>
          <input type="hidden" name="fb_id" value=""/>
          <div class="fb-register-notice"><?php s('en_EN'); ?>If you also would like to be able to log in using your email addres, please provide the following information.<?php e(); ?></div>
          <div class="row" id="email-field">
            <div class="span4">
              <div class="control-group">
                <label for="email" class="control-label"><?php s('en_EN'); ?>Email address<?php e(); ?></label>
                <div class="controls">
                  <input type="text" class="required email" id="email" name="email"/>
                </div>
              </div>
            </div>
          </div>
          <!-- existing email account -->
          <div id="login-email-exist">
            <div class="row">
              <div class="span4">
                <div class="control-group">
                  <label for="password" class="control-label"><?php s('en_EN'); ?>Password<?php e(); ?></label>
                  <div class="controls">
                    <span class="register_info"><?php s('en_EN'); ?>(6 characters minimum)<?php e(); ?></span><input type="password" class="required" minlength="6" id="password" name="password"/>
                  </div>
                </div>
              </div>
              <div class="forgot-pass-txt span8"><?php s('en_EN'); ?>If you forgot your password, leave this field empty and click "I forgot my password", we will then send you a mail to login and reset your password.<?php e(); ?></div>
            </div>
          </div>
          <button id="email-login-submit" class="btn-red"><?php s('fi_FI'); ?>Kirjaudu sisään<?php e(); ?></button><button id="email-login-nomail-submit" class="btn-red"><?php s('en_EN'); ?>I forgot my password<?php e(); ?></button>
          <!-- email account does not exist -->
          <div id="login-email-dont-exist">
            <p><?php s('en_EN'); ?>This email address is not in our records. Do you want to create an account using this email address, or did you make a mistake while typing your address?<?php e(); ?></p>
            <button id="email-re-login-btn" class="btn-red"><?php s('en_EN'); ?>I made a mistake, let's try again<?php e(); ?></button><button id="email-new-account-btn" class="btn-red"><?php s('en_EN'); ?>Create a new account<?php e(); ?></button>
          </div>
          <!-- register new account for email -->
          <div id="register-email">
            <div class="row" id="retype-password">
              <div class="span4">
                <div class="control-group">
                  <label for="password_verify"><?php s('en_EN'); ?>Re-enter password for verification<?php e(); ?></label>
                  <div class="controls">
                    <input type="password" class="required" id="password_verify" name="password_verify"/>
                  </div>
                </div>
              </div>
            </div>
            <p><?php s(); ?>Tätä tietoa ei julkaista nettisivuilla. Käytämme sitä ainoastaan, mikäli meidän pitää ottaa yhteyttä sinuun.<?php e(); ?></p>
            <div class="row">
              <div class="span4">
                <div class="control-group">
                  <label for="user_name"><?php s('en_EN'); ?>Name<?php e(); ?></label>
                  <div class="controls">
                    <input type="text" id="user_name" name="user_name" class="required"/>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="span4">
                <div class="control-group">
                  <label for="phone"><?php s('en_EN'); ?>Phone number<?php e(); ?></label>
                  <div class="controls">
                    <input type="text" id="phone" name="phone" class="required"/>
                  </div>
                </div>
              </div>
            </div>
            <button id="email-cancel-account-btn" class="btn-red"><?php s('en_EN'); ?>Cancel<?php e(); ?></button><button id="email-save-account-btn" class="btn-red"><?php s('en_EN'); ?>Create a new account<?php e(); ?></button>
          </div>
        </fieldset>  
      </form>