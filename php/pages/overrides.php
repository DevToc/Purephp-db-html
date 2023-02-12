<?php
session_start();
include("/var/www/html/global/php/user/access.php");
//access("USERPROD");
include("/var/www/html/product/php/pages/prd_header.php");
?>
<?php include_once('/var/www/html/global/php/gbl_connect.php'); ?>

<?php

$querya = "select wholesaler_abbreviation from gbl_wholesaler_info where wholesaler_active ='Y'";
$stma = $pdo_global->prepare($querya);
$stma->execute();
$abbs = $stma->fetchALL(PDO::FETCH_ASSOC);

$querya = "select distinct country from gbl_platforms";
$stma = $pdo_global->prepare($querya);
$stma->execute();
$countries = $stma->fetchALL(PDO::FETCH_ASSOC);
$keyword = "";

?>
<link rel="stylesheet" href="../../css/bootstrap.min.css">
<div class="row d-flex justify-content-around mb-4">
  <input type="text" name="search" id="search" class="form-control col-4" placeholder="Search..." value="<?= $keyword ?>">
  <button type="button" class="btn btn-primary form-control col-2" data-bs-toggle="modal" data-bs-target="#myModal">
    New
  </button>
</div>

<div class="datalist col-12 mx-auto">
  <table class="table table-striped text-center">
    <thead style="background-color:antiquewhite">
      <tr style="font-size: 1.2rem;">
        <th style="color:black" class="col-1">
          Wholesaler Sku
        </th>
        <th style="color:black" class="col-1">
          Wholesaler option
        </th>
        <th style="color:black" class="col-1">
          Wholesaler
        </th>
        <th style="color:black" class="col-1">
          BO Date
        </th>
        <th style="color:black" class="col-1">
          Discontinued
        </th>
        <th style="color:black" class="col-1">
          Country
        </th>
        <th style="color:black" class="col-1">
          Action
        </th>
      </tr>
    </thead>
    <tbody id="body">

    </tbody>

  </table>
  <div id="error"></div>
</div>
<!-- Add Modal -->
<!-- The Modal -->
<div class="modal" id="myModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title text-center">New Override</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">

        <form id="addForm">

          <div class="item row mb-3  d-flex justify-content-center">
            <label for="" class="col-4 col-form-label">Wholesaler Sku:</label>
            <input type="text" class="col-6 form-control" name="wholesaler_sku1" required>
          </div>

          <div class="item row mb-3  d-flex justify-content-center align-items-center">
            <label for="" class="col-4 col-form-label">Wholesaler Option:</label>
            <input type="text" name="wholesaler_option1" class="col-6 form-control" >
          </div>

          <div class="item row mb-3  d-flex justify-content-center">
            <label for="" class="col-4 col-form-label">Wholesaler</label>
            <select name="wholesaler1" id="" class="col-6 form-select">
              <?php
              foreach ($abbs as $abb) {
                echo "<option value=" . $abb["wholesaler_abbreviation"] . ">" . $abb["wholesaler_abbreviation"] . "</option>";
              }
              ?>
            </select>
          </div>

          <div class="item row mb-3  d-flex justify-content-center">
            <label for="" class="col-4 col-form-label">BO Date</label>
            <input type="date" name="BO_Date1" class="col-6 form-control" required>
          </div>

          <div class="item row mb-3  d-flex justify-content-center">
            <label for="" class="col-4 col-form-label">Discontinued</label>
            <select name="discontinued1" id="" class="col-6 form-select">
              <option value="0">0</option>
              <option value="1">1</option>
            </select>
          </div>

          <div class="item row mb-3  d-flex justify-content-center">
            <label for="" class="col-4 col-form-label">Country</label>
            <select name="country1" id="" class="col-6 form-select">
              <option value="ALL">All</option>
              <?php
              foreach ($countries as $country) {
                echo "<option value=" . $country["country"] . ">" . $country["country"] . "</option>";
              }
              ?>
            </select>
          </div>
          <div class="d-flex justify-content-around mx-4">
            <button class="btn btn-outline-danger" id="add" type="submit">Add</button>
            <button type="button" class="btn btn-outline-black" data-bs-dismiss="modal">Close</button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>


<!-- The Edit Modal -->
<div class="modal" id="editModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title text-center">Edit</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">

        <form id="editForm">
          <input type="text" name="editId" hidden>
          <div class="item row mb-3  d-flex justify-content-center">
            <label for="" class="col-4 col-form-label">Wholesaler Sku</label>
            <input type="text" name="wholesaler_sku" disabled class="col-6 form-control">
          </div>
          <div class="item row mb-3  d-flex justify-content-center">
            <label for="" class="col-4 col-form-label"> Wholesaler Option</label>
            <input type="text" name="wholesaler_option" disabled class="col-6 form-control">
          </div>

          <div class="item row mb-3  d-flex justify-content-center">
            <label for="" class="col-4 col-form-label">Wholesaler</label>
            <input type="text" name="wholesaler" disabled id="" class="col-6 form-control">
          </div>

          <div class="item row mb-3  d-flex justify-content-center">
            <label for="" class="col-4 col-form-label">BO Date</label>
            <input type="date" name="BO_Date" class="col-6 form-control">
          </div>

          <div class="item row mb-3  d-flex justify-content-center">
            <label for="" class="col-4 col-form-label">Discontinued</label>
            <select name="discontinued" id="" class="col-6 form-select">
              <option value="0">0</option>
              <option value="1">1</option>
            </select>
          </div>

          <div class="item row mb-3  d-flex justify-content-center">
            <label for="" class="col-4 col-form-label">Country</label>
            <select name="country" id="" class="col-6 form-select">
              <option value="ALL">All</option>
              <?php
              foreach ($countries as $country) {
                echo "<option value=" . $country["country"] . ">" . $country["country"] . "</option>";
              }
              ?>
            </select>
          </div>
          <div class="item row mb-3 d-flex justify-content-center col-10 mx-auto ">
            <button class="btn btn-outline-primary form-control" id="save" type="submit">Save</button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>


<!-- Delete Modal -->
<div class="modal" id="deleteModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title text-center">Delete</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">

        <form id="deleteForm">

        <div class="item row mb-3  d-flex justify-content-center">
            <label for="" class="col-4 col-form-label">Wholesaler Sku</label>
            <input type="text" name="wholesaler_sku" disabled class="col-6 form-control">
          </div>
          <input type="text" name="deleteId" hidden>
          <div class="item row mb-3  d-flex justify-content-center">
            <label for="" class="col-4 col-form-label">Wholesaler Option</label>
            <input type="text" name="wholesaler_option" disabled class="col-6 form-control">
          </div>

          <div class="item row mb-3  d-flex justify-content-center">
            <label for="" class="col-4 col-form-label">Wholesaler</label>
            <input type="text" name="wholesaler" disabled class="col-6 form-control">
          </div>
          <div class="d-flex justify-content-around mx-4">

          <button class="btn btn-danger form-control mr-1" type="submit" id="add" data-bs-dismiss="modal">Yes</button>
          <button type="button" class="btn btn-secondary form-control ml-1" data-bs-dismiss="modal">No</button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>

<?php include("/var/www/html/product/php/pages/prd_footer.php"); ?>


<script>
  var filter = null; // Data to be displayed
  var keyword = ""; // search keyword

  //show delete modal
  function deleteOne(id) {
    let data = filter.find((item) => item.id == id);
    $("#deleteModal").modal('toggle');
    $("input[name='wholesaler_sku']").val(data.wholesaler_sku);
    $("input[name='wholesaler_option']").val(data.wholesaler_option);
    $("input[name='wholesaler']").val(data.wholesaler);
    $("input[name='deleteId']").val(id);
  }

  // Shoe edit modal
  function editOne(id) {

    let data = filter.find((item) => item.id == id);
    let bodate = data.bo_date;
    let arr = bodate.split("/");

    $("#editModal").modal('toggle');
    $("input[name='wholesaler_sku']").val(data.wholesaler_sku);
    $("input[name='wholesaler_option']").val(data.wholesaler_option);
    $("input[name='wholesaler']").val(data.wholesaler);
    $("input[name='BO_Date']").val(arr[2] + "-" + arr[0] + "-" + arr[1]);
    $("select[name='discontinued']").val(data.discontinued);
    $("select[name='country']").val("ALL");
    $("input[name='editId']").val(id);
  }

  function initDatalists() {
    $.ajax({
      method: 'POST',
      url: '../functions/overrides_op.php',
      data: {
        type: "search",
        keyword: keyword
      },
      success: function(response) {
        if (response == "") {
          $('#body').html("");
          $('#error').html("<h1 class='text-center'>No Result</h1>");
          return;
        }
        $('#error').html("");
        var data = JSON.parse(response);
        let filterdata = data.data;
        filter = filterdata;
        let inData = "";
        filterdata.map((item) => {
          inData += "<tr><td>" + item.wholesaler_sku + "</td><td>" + item.wholesaler_option + "</td><td>" + item.wholesaler + "</td><td>" + item.bo_date + "</td><td>" + item.discontinued + "</td><td>" + item.country + `</td><td><button class='btn btn-outline-info'  onclick='editOne(${item.id})'>Edit</button><button class='btn btn-outline-danger ml-2' onclick='deleteOne(${item.id})'>Delete</button>` + "</tr>";
        })
        $('#body').html(inData);
      }
    });
  }

  $("#search").on('keyup', function(event) {
    keyword = $("#search").val();
    initDatalists();
  });


  $('#addForm').on('submit', function(e) {
    //e.preventDefault();
    let addData = $("#addForm").serialize()
    $.ajax({
      method: 'POST',
      url: '../functions/overrides_op.php',
      data: {
        type: "add",
        addData: addData
      },
      success: function(respoonse) {
        $("#myModal").modal('toggle');
        initDatalists();
      }
    });

  });
  $('#editForm').on('submit', function(e) {
    e.preventDefault();
    let editData = $("#editForm").serialize();
    $.ajax({
      method: 'POST',
      url: '../functions/overrides_op.php',
      data: {
        type: "edit",
        editData: editData
      },
      success: function(respoonse) {
        console.log(respoonse);
        initDatalists();
        $('#editModal').modal('toggle');

      }
    });

  });


  $('#deleteForm').on('submit', function(e) {
    e.preventDefault();
    //$("#deleteModal").modal('toggle');
    let id = $("input[name='deleteId']").val();
    $.ajax({
      method: 'POST',
      url: '../functions/overrides_op.php',
      data: {
        type: "delete",
        id: id
      },
      success: function(respoonse) {
        initDatalists();
      }
    });

  });

  //Get Data from database firstly
  initDatalists();
</script>
