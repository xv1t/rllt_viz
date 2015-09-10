<!-- 
This styles for the internal rules: no libs, no requires
-->
<style>
    table.table tbody tr.success td{
        /*background-color: #c1e2b3 */
		background-color: green;
		color: white;
		
    }
    
    table.table tbody tr.danger td{
        /*background-color: #ebcccc */
		background-color: red;
		color: white;
    }
    
    table.table {
        width: 100%;
    }
    
    table.table tr td {
        padding: 5px;
		text-align: center;
    }
    
    table.table tr.danger:hover td{
        background-color: firebrick;
        /*color: white; */
    }
    
    table.table tr.success:hover td    
    {
        background-color: darkolivegreen;
        /* color: white; */
    }
    
    table.table thead tr th{
        font-weight: normal;
        background-color: #d4d4d4;
    }
    
    .container-fluid{

    }
    
    body, html{
        font-family: arial;
    }
    
    .text-right{
        text-align: right;
    }
    
    h2 {
        font-weight: normal;
    }
    
    h2 small{
        font-size: 70%;
        font-weight: normal;
        color: grey;
    }
</style>