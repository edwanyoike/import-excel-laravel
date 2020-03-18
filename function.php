    public function importFromExcel(Request $request)
    {

        try {
            $this->validate($request, [
                   'file' => 'required|mimes:xls,xlsx'
            ]);
        } catch (ValidationException $e) {
            return back()->with('error', 'An error occurred while validating data');
        }

        try {

            $path = $request->file('file')->getRealPath();

            $reader = IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);

            $worksheet = $spreadsheet->getActiveSheet();
            $rows = [];
            foreach ($worksheet->getRowIterator() AS $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(true); // This doesn't loops through all cells only them with data,
                $cells = [];
                foreach ($cellIterator as $cell) {
                    $cells[] = $cell->getValue();
                }
                $rows[] = $cells;
            }


            //
            if (count($rows) > 0) {
                unset($rows[0]);

                try {

                    foreach ($rows as $row) {

                        $churchCode = $row[6];

                   //     $church = DB::table('churches')->where('code', $churchCode)->first();

                        $church = Church::where('code', $churchCode)->first();


                        if ($church !== null) {
                            $firstName = $row[0];
                            $secondName = $row[1];
                            $gender = $row[2];

                            $phoneNumber = $row[3];
                            $emailAddress = $row[4];
                            $location = $row[5];

                            $member = new Member();
                            $member->firstName = $firstName;
                            $member->secondName = $secondName;
                            $member->gender = $gender;


                            $church->members()->save($member);

                            $member->save();

                            $address = new Address();

                            $address->phoneNumber = $phoneNumber;
                            $address->emailAddress = $emailAddress;
                            $address->location = $location;


                            $member->address()->save($address);


                        } else {
                            return back()->with('error', 'specify a church code in the system');
                        }


                    }
                    //

                } catch (Exception $e) {
                    return back()->with('error', $e->getMessage() );

                }


            }
        } catch (Exception $e) {
            return back()->with('error', 'An error occurred while reading file');

        }

        return back()->with('success', 'members imported successfully');

    }
