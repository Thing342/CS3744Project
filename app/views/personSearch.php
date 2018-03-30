<!--A form that users can fill out.  Users can fill this form out to search for entries other characters have created and made public.-->
<!--https://www.w3schools.com/howto/howto_css_contact_form.asp-->
<div class="containerForForm">
  <form method="POST" action="app/views/SearchPersonScript.php">
    <label for="lname">Soldier Last Name (Must be exact)</label>
    <input type="text" id="lname" name="lname" placeholder="Last name">
    <input name="submit" type="submit" value="Submit" id="submitFancy">
  </form>
</div>