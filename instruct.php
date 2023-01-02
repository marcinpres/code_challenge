<?php

# The function is designed to run from terminal (CLI) and allows to read and process CSV files when the requirements criteria are met.
# As an output user receives and information such as services, centre and ref id based on selected country code.
# The only parameter - $csv_location - the filename path comes from the console input.

function csv_reader()
	{
	#Lets provide some basic options
	echo PHP_EOL."The script allows to read and process CSV files".PHP_EOL;
	echo PHP_EOL.'Please, type:'.PHP_EOL.'"help" to read the requirements criteria.'.PHP_EOL.'"run" to execute the script.'.PHP_EOL.'"exit" to quit.'.PHP_EOL; 

	$options = readline(' ');
	if(strtolower($options) == 'exit')
		{
		exit();
		}
	else if(strtolower($options) == 'help')
		{
		echo PHP_EOL
		.'The table should consist of the following header'.PHP_EOL
		."+".str_repeat("-",34)."+".PHP_EOL
		."| Ref | Centre | Service | Country |".PHP_EOL
		."+".str_repeat("-",34)."+".PHP_EOL
		.PHP_EOL;	
		}
	else if(strtolower($options) == 'run')
		{	
		
		#Retrieve csv file location
		$csv_location = readline('Please enter a file path: ');
		$csv_location = trim($csv_location," ");	
		
		#Check if the file exists
		if(!file_exists($csv_location))
			{
			echo "Failed to open stream: No such file or directory in ".$csv_location.PHP_EOL;	
			exit();
			}
		#Check if the user has permission to read the file
		if(!is_readable($csv_location))
			{
			echo "Failed to open stream: Permission denied for ".$csv_location.PHP_EOL;	
			exit();
			}		

		#Set initial columns lengths 
		$column_length = array(0, 0, 0);

		#Read a file into an array
		$csv = array();
		$lines = file($csv_location, FILE_IGNORE_NEW_LINES);

		foreach ($lines as $key => $value)
			{
			$csv[$key] = str_getcsv($value);
			
			#Verify the data structure
			if($csv[0][3] !== "Country" || $csv[0][2] !== "Service" || $csv[0][1] !== "Centre" || $csv[0][0] !== "Ref")
				{
				echo "Incorrect data structure, processing failed.";
				return;
				}
			
			#Find the longest string in each column
			$column_length[0] = strlen($csv[$key][0]) > $column_length[0] ? strlen($csv[$key][0]) : $column_length[0];
			$column_length[1] = strlen($csv[$key][1]) > $column_length[1] ? strlen($csv[$key][1]) : $column_length[1];
			$column_length[2] = strlen($csv[$key][2]) > $column_length[2] ? strlen($csv[$key][2]) : $column_length[2]; 
			}

		#Read user input
		echo PHP_EOL;
		$cc = readline('Please enter a country code: ');

		#Set the table header 
		$table = PHP_EOL;
		$table.="|Reference".str_repeat(" ",abs(7 - $column_length[0]))."| Centre".str_repeat(" ",abs(5 - $column_length[1]))."| Service".str_repeat(" ",abs(6 - $column_length[1]))."|".PHP_EOL;
		$table.="+".str_repeat("-",$column_length[0]+$column_length[1]+$column_length[2]+8)."+".PHP_EOL;

		$result = 0;
		for($i=1;$i<count($csv);$i++)
			{
				
			#Create an array for summary output, to show the total number of services in each country.
			$services[$i-1]	= strtoupper($csv[$i][3]);
			
			#Convert country code characters to lowercase	
			$csv[$i][3] = strtolower($csv[$i][3]);
			
			if (strtolower($cc) == $csv[$i][3])
				{
				
				$result = 1;	
				
				#Display data in table	
				$table.="| " 
				. $csv[$i][0] . str_repeat(" ",abs(strlen($csv[$i][0]) - $column_length[0]))." | "
				. $csv[$i][1] . str_repeat(" ",abs(strlen($csv[$i][1]) - $column_length[1]))." | "
				. $csv[$i][2] . str_repeat(" ",abs(strlen($csv[$i][2]) - $column_length[2]))." | "
				. PHP_EOL;	
				}
			}

		$table.="+".str_repeat("-",$column_length[0]+$column_length[1]+$column_length[2]+8)."+".PHP_EOL;

		#No matching data returned
		if($result == 0)
			{
			echo 'No records found, try different country code.'.PHP_EOL;
			$table='';		
			}

		echo $table.PHP_EOL; 

		$services = array_count_values($services);

		#Create table for the summary
		echo "Total number of services in each country:".PHP_EOL;
		$table=str_repeat("---",11).PHP_EOL;
		$table.="country code | number of services".PHP_EOL;
		$table.=str_repeat("---",11).PHP_EOL;
		foreach($services as $key => $value)
			{
			$table.=str_pad($key,13," ",STR_PAD_BOTH)."|".str_pad($value,17," ",STR_PAD_BOTH).PHP_EOL;
			}

		echo $table.PHP_EOL;

		}
	else 
		{
		echo "Invalid command."; exit();	
		}
	}

#Run the script
csv_reader();

?>
