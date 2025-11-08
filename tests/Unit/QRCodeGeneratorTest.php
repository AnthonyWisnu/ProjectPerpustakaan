
<?php

namespace Tests\Unit;

use App\Services\QRCodeGenerator;
use Tests\TestCase;

class QRCodeGeneratorTest extends TestCase
{
    protected QRCodeGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new QRCodeGenerator();
    }

    /**
     * Test generate reservation code with ID
     */
    public function test_generate_reservation_code_with_id(): void
    {
        $code = $this->generator->generateReservationCode(123);

        $this->assertEquals('RSV000123', $code);
    }

    /**
     * Test generate reservation code without ID
     */
    public function test_generate_reservation_code_without_id(): void
    {
        $code = $this->generator->generateReservationCode();

        $this->assertStringStartsWith('RSV', $code);
        $this->assertEquals(9, strlen($code)); // RSV + 6 random chars
    }

    /**
     * Test generate loan code with ID
     */
    public function test_generate_loan_code_with_id(): void
    {
        $code = $this->generator->generateLoanCode(456);

        $this->assertEquals('LOAN000456', $code);
    }

    /**
     * Test generate loan code without ID
     */
    public function test_generate_loan_code_without_id(): void
    {
        $code = $this->generator->generateLoanCode();

        $this->assertStringStartsWith('LOAN', $code);
        $this->assertEquals(10, strlen($code)); // LOAN + 6 random chars
    }

    /**
     * Test generate barcode with ID
     */
    public function test_generate_barcode_with_id(): void
    {
        $code = $this->generator->generateBarcode(789);

        $this->assertEquals('BK00000789', $code);
    }

    /**
     * Test generate barcode without ID
     */
    public function test_generate_barcode_without_id(): void
    {
        $code = $this->generator->generateBarcode();

        $this->assertStringStartsWith('BK', $code);
        $this->assertEquals(10, strlen($code)); // BK + 8 random chars
    }

    /**
     * Test generate member number
     */
    public function test_generate_member_number(): void
    {
        $code = $this->generator->generateMemberNumber(42);

        $this->assertEquals('MBR000042', $code);
    }

    /**
     * Test validate reservation code valid
     */
    public function test_validate_reservation_code_valid(): void
    {
        $isValid = $this->generator->validateReservationCode('RSV000123');

        $this->assertTrue($isValid);
    }

    /**
     * Test validate reservation code invalid prefix
     */
    public function test_validate_reservation_code_invalid_prefix(): void
    {
        $isValid = $this->generator->validateReservationCode('LOAN000123');

        $this->assertFalse($isValid);
    }

    /**
     * Test validate reservation code too short
     */
    public function test_validate_reservation_code_too_short(): void
    {
        $isValid = $this->generator->validateReservationCode('RSV12');

        $this->assertFalse($isValid);
    }

    /**
     * Test validate loan code valid
     */
    public function test_validate_loan_code_valid(): void
    {
        $isValid = $this->generator->validateLoanCode('LOAN000456');

        $this->assertTrue($isValid);
    }

    /**
     * Test validate loan code invalid prefix
     */
    public function test_validate_loan_code_invalid_prefix(): void
    {
        $isValid = $this->generator->validateLoanCode('RSV000456');

        $this->assertFalse($isValid);
    }

    /**
     * Test validate loan code too short
     */
    public function test_validate_loan_code_too_short(): void
    {
        $isValid = $this->generator->validateLoanCode('LOAN123');

        $this->assertFalse($isValid);
    }

    /**
     * Test validate barcode valid
     */
    public function test_validate_barcode_valid(): void
    {
        $isValid = $this->generator->validateBarcode('BK00000789');

        $this->assertTrue($isValid);
    }

    /**
     * Test validate barcode invalid prefix
     */
    public function test_validate_barcode_invalid_prefix(): void
    {
        $isValid = $this->generator->validateBarcode('XX00000789');

        $this->assertFalse($isValid);
    }

    /**
     * Test validate barcode too short
     */
    public function test_validate_barcode_too_short(): void
    {
        $isValid = $this->generator->validateBarcode('BK123');

        $this->assertFalse($isValid);
    }

    /**
     * Test extract reservation ID valid
     */
    public function test_extract_reservation_id_valid(): void
    {
        $id = $this->generator->extractReservationId('RSV000123');

        $this->assertEquals(123, $id);
    }

    /**
     * Test extract reservation ID with leading zeros
     */
    public function test_extract_reservation_id_with_leading_zeros(): void
    {
        $id = $this->generator->extractReservationId('RSV000042');

        $this->assertEquals(42, $id);
    }

    /**
     * Test extract reservation ID invalid format
     */
    public function test_extract_reservation_id_invalid_format(): void
    {
        $id = $this->generator->extractReservationId('INVALID');

        $this->assertNull($id);
    }

    /**
     * Test extract reservation ID with non-numeric code
     */
    public function test_extract_reservation_id_non_numeric(): void
    {
        $id = $this->generator->extractReservationId('RSVABCDEF');

        $this->assertNull($id);
    }

    /**
     * Test extract loan ID valid
     */
    public function test_extract_loan_id_valid(): void
    {
        $id = $this->generator->extractLoanId('LOAN000456');

        $this->assertEquals(456, $id);
    }

    /**
     * Test extract loan ID invalid format
     */
    public function test_extract_loan_id_invalid_format(): void
    {
        $id = $this->generator->extractLoanId('INVALID');

        $this->assertNull($id);
    }

    /**
     * Test extract loan ID with non-numeric code
     */
    public function test_extract_loan_id_non_numeric(): void
    {
        $id = $this->generator->extractLoanId('LOANABCDEF');

        $this->assertNull($id);
    }

    /**
     * Test extract book ID valid
     */
    public function test_extract_book_id_valid(): void
    {
        $id = $this->generator->extractBookId('BK00000789');

        $this->assertEquals(789, $id);
    }

    /**
     * Test extract book ID with leading zeros
     */
    public function test_extract_book_id_with_leading_zeros(): void
    {
        $id = $this->generator->extractBookId('BK00000001');

        $this->assertEquals(1, $id);
    }

    /**
     * Test extract book ID invalid format
     */
    public function test_extract_book_id_invalid_format(): void
    {
        $id = $this->generator->extractBookId('INVALID');

        $this->assertNull($id);
    }

    /**
     * Test extract book ID with non-numeric code
     */
    public function test_extract_book_id_non_numeric(): void
    {
        $id = $this->generator->extractBookId('BKABCDEFGH');

        $this->assertNull($id);
    }

    /**
     * Test generated codes are uppercase
     */
    public function test_generated_codes_are_uppercase(): void
    {
        $resCode = $this->generator->generateReservationCode();
        $loanCode = $this->generator->generateLoanCode();
        $barcode = $this->generator->generateBarcode();

        $this->assertEquals(strtoupper($resCode), $resCode);
        $this->assertEquals(strtoupper($loanCode), $loanCode);
        $this->assertEquals(strtoupper($barcode), $barcode);
    }

    /**
     * Test member number pads correctly
     */
    public function test_member_number_pads_correctly(): void
    {
        $shortNumber = $this->generator->generateMemberNumber(5);
        $longNumber = $this->generator->generateMemberNumber(123456);

        $this->assertEquals('MBR000005', $shortNumber);
        $this->assertEquals('MBR123456', $longNumber);
    }
}
